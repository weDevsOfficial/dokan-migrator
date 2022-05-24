import { useState, useEffect,} from 'react'
import axios from 'axios'
import { Progress, Spin, Button, Col, Card, Typography, Divider, } from "antd";

const { Title, Text } = Typography;

const DokanMigrator = (props) => {

        // States.
        const [ ajaxurl ] = useState(props.url);
        const [ importType, setImportType ] = useState(props.type);
        const [ countAction ] = useState('dokan_migrator_count_data');
        const [ vendorMigrateAction ] = useState('dokan_migrator_import_data');

        const [ nonce ] = useState(props.nonce);
        const [ number, setNumner ] = useState(props.number);
        const [ offset, setOffset ] = useState(0);
        const [ totalCount, setTotalCount ] = useState(0);
        const [ totalMigrated, setTotalMigrated ] = useState(0);
        const [ progress, setProgress ] = useState(0);
        const [ progressStatus, setProgressStatus ] = useState("active");
        const [ isLoading, setIsLoading ] = useState(true);

        useEffect(() => {
            updateProgressbar();
        }, [totalMigrated, totalCount]);


        useEffect(() => {
            countVendors( props.startAutoMigration );
        },[props.startAutoMigration ]);

        useEffect(() => {
            props.lastCompleted ? setTotalMigrated( totalCount  ) : '';
        },[props.lastCompleted, totalCount ]);

        useEffect(() => {
            props.updateLoading(isLoading);
        },[isLoading]);

        // Functions.
        const migrateVendorsHandler = ( from = offset, totalItem = totalCount, getMigrated = totalMigrated ) => {
            setIsLoading(true);
            sendRequest(
                {
                    action: vendorMigrateAction,
                    nonce: nonce,
                    import: importType,
                    number: number,
                    offset: from,
                    total_count: totalItem,
                    total_migrated: getMigrated
                }
            )
            .then(function (response) {
                let resp = response.data ? response.data : response;
                if ( resp.success ) {
                    setTotalMigrated(resp.data.process.total_migrated);
                    if ( resp.data.process && resp.data.process.migrated != 0 ) {
                        setOffset(resp.data.process.next);
                        migrateVendorsHandler( resp.data.process.next, totalItem, resp.data.process.total_migrated );
                    }
                } else {
                    setIsLoading(false);

                    props.updateMigrationState(props.type);
                }
            })
            .catch(function (error) {
                console.error(error);
            });
        }
        // const getVendorsHandler = () => {
        //     countVendors( true );
        // }

        const countVendors = ( startMigration = false ) => {
            setIsLoading(true);
            sendRequest(
                {
                    action: countAction,
                    import: importType,
                    nonce: nonce,
                }
            )
            .then(function (response) {
                let resp = response.data ? response.data : response;
                if ( resp.success ) {
                    let respNext = 0;
                    let respTotalMigrated = 0;
                    if ( false != resp.data.migrate.old_migrated_status && resp.data.migrate.old_migrated_status.next ) {
                        respNext = resp.data.migrate.old_migrated_status.next;
                        respTotalMigrated = resp.data.migrate.old_migrated_status.total_migrated;
                        setOffset(respNext);

                    }
                    setTotalCount( resp.data.migrate.total_count );

                    props.lastCompleted ? setTotalMigrated( resp.data.migrate.total_count  ) : setTotalMigrated(respTotalMigrated);

                    ! startMigration ? setIsLoading(false) : '';
                    startMigration ? migrateVendorsHandler( respNext, resp.data.migrate.total_count, respTotalMigrated ) : '';

                } else {
                    console.error(resp.data.message);
                }
            })
            .catch(function (error) {
                console.error(error);
            });
        }

        const sendRequest = async ( data ) => {
            data.migratable = props.migrate;

            return await axios.post( ajaxurl, null, { params: data } );
        }

        const updateProgressbar = () => {
            let progressCount       = Math.floor( ( totalMigrated * 100 ) / totalCount );
            totalCount == 0 ? progressCount = 100 : '';
            let progressCountStatus = progressCount == 100 ? "success": "active";
            setProgressStatus(progressCountStatus)
            setProgress(progressCount);
        }

        // UI.
        return (
            <>
                <Col xs={24} sm={24} md={8} lg={8} xl={8}>
                    <Card className="dokan-migrator-card">
                    <div className="dokan-migrator-align-center">
                        <Text style={{marginBottom: '20px'}}>{ isLoading ? <Spin size="small" /> : '' } {props.title}</Text>
                        <Progress
                        type="circle"
                        percent={progress}
                        style={{marginBottom: '20px'}}
                        />
                        <div className="dokan-migration-count-status">
                        <div className="dokan-migration-count-total">
                            <Title style={{textAlign: 'center'}} level={3}>{totalCount}</Title>
                            <Text style={{textAlign: 'center', marginTop: '-15px'}} type="secondary">Total</Text>
                        </div>
                        <Divider type="vertical" style={{height: '40px', marginTop: '10px'}}/>
                        <div className="dokan-migration-count-total">
                            <Title style={{textAlign: 'center'}} level={3}>{totalMigrated}</Title>
                            <Text style={{textAlign: 'center', marginTop: '-15px'}} type="secondary">Migrated</Text>
                        </div>
                        </div>
                    </div>
                    </Card>
                </Col>
            </>
        );
}

export default DokanMigrator;